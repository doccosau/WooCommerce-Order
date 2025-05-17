"use client"

import { useState } from "react"
import { startSync } from "../actions"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Checkbox } from "@/components/ui/checkbox"
import { useToast } from "@/components/ui/use-toast"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { Loader2, RefreshCw } from "lucide-react"

// Schema validation cho form đồng bộ
const syncFormSchema = z.object({
  website_id: z.string().uuid("ID website không hợp lệ"),
  entity_type: z.enum(["products", "orders", "customers", "all"], {
    required_error: "Vui lòng chọn loại dữ liệu",
  }),
  sync_all: z.boolean().default(false),
})

type SyncFormValues = z.infer<typeof syncFormSchema>

interface SyncDashboardProps {
  websites: any[]
}

export function SyncDashboard({ websites }: SyncDashboardProps) {
  const { toast } = useToast()
  const [isSubmitting, setIsSubmitting] = useState(false)

  // Khởi tạo form
  const form = useForm<SyncFormValues>({
    resolver: zodResolver(syncFormSchema),
    defaultValues: {
      website_id: websites[0]?.id || "",
      entity_type: "all",
      sync_all: false,
    },
  })

  // Xử lý submit form
  async function onSubmit(values: SyncFormValues) {
    setIsSubmitting(true)

    try {
      // Chuyển đổi values thành FormData
      const formData = new FormData()
      formData.append("website_id", values.website_id)
      formData.append("entity_type", values.entity_type)
      formData.append("sync_all", values.sync_all.toString())

      // Gọi server action
      const result = await startSync(formData)

      if (result.success) {
        toast({
          title: "Đồng bộ dữ liệu đã bắt đầu",
          description: "Quá trình đồng bộ đang được thực hiện trong nền.",
        })
      } else {
        toast({
          title: "Lỗi",
          description: result.error || "Có lỗi xảy ra khi bắt đầu đồng bộ dữ liệu.",
          variant: "destructive",
        })
      }
    } catch (error) {
      toast({
        title: "Lỗi",
        description: "Có lỗi xảy ra khi bắt đầu đồng bộ dữ liệu.",
        variant: "destructive",
      })
    } finally {
      setIsSubmitting(false)
    }
  }

  // Xử lý khi thay đổi sync_all
  const watchSyncAll = form.watch("sync_all")

  return (
    <div className="grid gap-4 md:grid-cols-2">
      <Card>
        <CardContent className="pt-6">
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
              <FormField
                control={form.control}
                name="website_id"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Website</FormLabel>
                    <Select
                      disabled={watchSyncAll || isSubmitting}
                      onValueChange={field.onChange}
                      defaultValue={field.value}
                    >
                      <FormControl>
                        <SelectTrigger>
                          <SelectValue placeholder="Chọn website" />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {websites.map((website) => (
                          <SelectItem key={website.id} value={website.id}>
                            {website.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormDescription>Chọn website để đồng bộ dữ liệu.</FormDescription>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="entity_type"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Loại dữ liệu</FormLabel>
                    <Select onValueChange={field.onChange} defaultValue={field.value}>
                      <FormControl>
                        <SelectTrigger>
                          <SelectValue placeholder="Chọn loại dữ liệu" />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        <SelectItem value="products">Sản phẩm</SelectItem>
                        <SelectItem value="orders">Đơn hàng</SelectItem>
                        <SelectItem value="customers">Khách hàng</SelectItem>
                        <SelectItem value="all">Tất cả</SelectItem>
                      </SelectContent>
                    </Select>
                    <FormDescription>Chọn loại dữ liệu cần đồng bộ.</FormDescription>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="sync_all"
                render={({ field }) => (
                  <FormItem className="flex flex-row items-start space-x-3 space-y-0 rounded-md border p-4">
                    <FormControl>
                      <Checkbox checked={field.value} onCheckedChange={field.onChange} />
                    </FormControl>
                    <div className="space-y-1 leading-none">
                      <FormLabel>Đồng bộ tất cả website</FormLabel>
                      <FormDescription>Đồng bộ dữ liệu từ tất cả các website đang hoạt động.</FormDescription>
                    </div>
                  </FormItem>
                )}
              />

              <Button type="submit" disabled={isSubmitting} className="w-full">
                {isSubmitting ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    Đang bắt đầu...
                  </>
                ) : (
                  <>
                    <RefreshCw className="mr-2 h-4 w-4" />
                    Bắt đầu đồng bộ
                  </>
                )}
              </Button>
            </form>
          </Form>
        </CardContent>
      </Card>

      <Card>
        <CardContent className="pt-6">
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium">Hướng dẫn đồng bộ dữ liệu</h3>
              <p className="text-sm text-muted-foreground mt-1">Quy trình đồng bộ dữ liệu từ WooCommerce</p>
            </div>

            <div className="space-y-2">
              <div className="flex items-center">
                <div className="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 mr-3">
                  1
                </div>
                <p className="text-sm">Chọn website hoặc tất cả website để đồng bộ</p>
              </div>

              <div className="flex items-center">
                <div className="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 mr-3">
                  2
                </div>
                <p className="text-sm">Chọn loại dữ liệu cần đồng bộ (sản phẩm, đơn hàng, khách hàng hoặc tất cả)</p>
              </div>

              <div className="flex items-center">
                <div className="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 mr-3">
                  3
                </div>
                <p className="text-sm">Nhấn "Bắt đầu đồng bộ" để khởi động quá trình</p>
              </div>

              <div className="flex items-center">
                <div className="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 mr-3">
                  4
                </div>
                <p className="text-sm">Theo dõi tiến trình trong tab "Lịch sử đồng bộ"</p>
              </div>
            </div>

            <div className="pt-2">
              <p className="text-sm text-muted-foreground">
                <strong>Lưu ý:</strong> Quá trình đồng bộ sẽ được thực hiện trong nền và có thể mất một thời gian tùy
                thuộc vào lượng dữ liệu.
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
