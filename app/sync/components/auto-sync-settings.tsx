"use client"

import { useState } from "react"
import { updateAutoSyncSettings } from "../actions"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Switch } from "@/components/ui/switch"
import { Checkbox } from "@/components/ui/checkbox"
import { Input } from "@/components/ui/input"
import { useToast } from "@/components/ui/use-toast"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { Loader2, Save } from "lucide-react"

// Schema validation cho form cài đặt đồng bộ tự động
const autoSyncSchema = z.object({
  enabled: z.boolean().default(false),
  frequency: z.enum(["daily", "weekly", "monthly"]),
  time: z.string().regex(/^([01]\d|2[0-3]):([0-5]\d)$/, {
    message: "Thời gian phải có định dạng HH:MM",
  }),
  entities: z.array(z.string()).min(1, {
    message: "Chọn ít nhất một loại dữ liệu",
  }),
})

type AutoSyncFormValues = z.infer<typeof autoSyncSchema>

interface AutoSyncSettingsProps {
  websites: any[]
  initialSettings: any
}

export function AutoSyncSettings({ websites, initialSettings }: AutoSyncSettingsProps) {
  const { toast } = useToast()
  const [isSubmitting, setIsSubmitting] = useState(false)

  // Khởi tạo form
  const form = useForm<AutoSyncFormValues>({
    resolver: zodResolver(autoSyncSchema),
    defaultValues: {
      enabled: initialSettings?.enabled || false,
      frequency: initialSettings?.frequency || "daily",
      time: initialSettings?.time || "00:00",
      entities: initialSettings?.entities || ["products"],
    },
  })

  // Xử lý submit form
  async function onSubmit(values: AutoSyncFormValues) {
    setIsSubmitting(true)

    try {
      // Chuyển đổi values thành FormData
      const formData = new FormData()
      formData.append("enabled", values.enabled.toString())
      formData.append("frequency", values.frequency)
      formData.append("time", values.time)
      values.entities.forEach((entity) => {
        formData.append("entities", entity)
      })

      // Gọi server action
      const result = await updateAutoSyncSettings(formData)

      if (result.success) {
        toast({
          title: "Đã lưu cài đặt",
          description: "Cài đặt đồng bộ tự động đã được cập nhật thành công.",
        })
      } else {
        toast({
          title: "Lỗi",
          description: result.error || "Có lỗi xảy ra khi cập nhật cài đặt.",
          variant: "destructive",
        })
      }
    } catch (error) {
      toast({
        title: "Lỗi",
        description: "Có lỗi xảy ra khi cập nhật cài đặt.",
        variant: "destructive",
      })
    } finally {
      setIsSubmitting(false)
    }
  }

  // Danh sách loại dữ liệu
  const entityTypes = [
    { id: "products", label: "Sản phẩm" },
    { id: "orders", label: "Đơn hàng" },
    { id: "customers", label: "Khách hàng" },
  ]

  return (
    <div className="grid gap-4 md:grid-cols-2">
      <Card>
        <CardContent className="pt-6">
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
              <FormField
                control={form.control}
                name="enabled"
                render={({ field }) => (
                  <FormItem className="flex flex-row items-center justify-between rounded-lg border p-4">
                    <div className="space-y-0.5">
                      <FormLabel className="text-base">Đồng bộ tự động</FormLabel>
                      <FormDescription>Bật/tắt tính năng đồng bộ dữ liệu tự động.</FormDescription>
                    </div>
                    <FormControl>
                      <Switch checked={field.value} onCheckedChange={field.onChange} />
                    </FormControl>
                  </FormItem>
                )}
              />

              <div className="space-y-4">
                <FormField
                  control={form.control}
                  name="frequency"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Tần suất</FormLabel>
                      <Select
                        onValueChange={field.onChange}
                        defaultValue={field.value}
                        disabled={!form.watch("enabled")}
                      >
                        <FormControl>
                          <SelectTrigger>
                            <SelectValue placeholder="Chọn tần suất" />
                          </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                          <SelectItem value="daily">Hàng ngày</SelectItem>
                          <SelectItem value="weekly">Hàng tuần</SelectItem>
                          <SelectItem value="monthly">Hàng tháng</SelectItem>
                        </SelectContent>
                      </Select>
                      <FormDescription>Tần suất đồng bộ dữ liệu tự động.</FormDescription>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={form.control}
                  name="time"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Thời gian</FormLabel>
                      <FormControl>
                        <Input type="time" {...field} disabled={!form.watch("enabled")} />
                      </FormControl>
                      <FormDescription>Thời gian bắt đầu đồng bộ (giờ địa phương).</FormDescription>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={form.control}
                  name="entities"
                  render={() => (
                    <FormItem>
                      <div className="mb-4">
                        <FormLabel className="text-base">Loại dữ liệu</FormLabel>
                        <FormDescription>Chọn loại dữ liệu cần đồng bộ tự động.</FormDescription>
                      </div>
                      {entityTypes.map((type) => (
                        <FormField
                          key={type.id}
                          control={form.control}
                          name="entities"
                          render={({ field }) => {
                            return (
                              <FormItem key={type.id} className="flex flex-row items-start space-x-3 space-y-0">
                                <FormControl>
                                  <Checkbox
                                    checked={field.value?.includes(type.id)}
                                    onCheckedChange={(checked) => {
                                      return checked
                                        ? field.onChange([...field.value, type.id])
                                        : field.onChange(field.value?.filter((value) => value !== type.id))
                                    }}
                                    disabled={!form.watch("enabled")}
                                  />
                                </FormControl>
                                <FormLabel className="font-normal">{type.label}</FormLabel>
                              </FormItem>
                            )
                          }}
                        />
                      ))}
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <Button type="submit" disabled={isSubmitting} className="w-full">
                {isSubmitting ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    Đang lưu...
                  </>
                ) : (
                  <>
                    <Save className="mr-2 h-4 w-4" />
                    Lưu cài đặt
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
              <h3 className="text-lg font-medium">Thông tin đồng bộ tự động</h3>
              <p className="text-sm text-muted-foreground mt-1">
                Hiểu cách thức hoạt động của tính năng đồng bộ tự động
              </p>
            </div>

            <div className="space-y-4">
              <div>
                <h4 className="font-medium">Tần suất đồng bộ</h4>
                <ul className="mt-2 space-y-2 text-sm text-muted-foreground">
                  <li className="flex items-start">
                    <span className="font-medium mr-2">•</span>
                    <span>
                      <strong>Hàng ngày:</strong> Đồng bộ dữ liệu một lần mỗi ngày vào thời gian đã chọn.
                    </span>
                  </li>
                  <li className="flex items-start">
                    <span className="font-medium mr-2">•</span>
                    <span>
                      <strong>Hàng tuần:</strong> Đồng bộ dữ liệu một lần mỗi tuần vào thời gian đã chọn (Thứ Hai).
                    </span>
                  </li>
                  <li className="flex items-start">
                    <span className="font-medium mr-2">•</span>
                    <span>
                      <strong>Hàng tháng:</strong> Đồng bộ dữ liệu một lần mỗi tháng vào thời gian đã chọn (ngày 1).
                    </span>
                  </li>
                </ul>
              </div>

              <div>
                <h4 className="font-medium">Loại dữ liệu</h4>
                <ul className="mt-2 space-y-2 text-sm text-muted-foreground">
                  <li className="flex items-start">
                    <span className="font-medium mr-2">•</span>
                    <span>
                      <strong>Sản phẩm:</strong> Đồng bộ thông tin sản phẩm từ các website WooCommerce.
                    </span>
                  </li>
                  <li className="flex items-start">
                    <span className="font-medium mr-2">•</span>
                    <span>
                      <strong>Đơn hàng:</strong> Đồng bộ thông tin đơn hàng từ các website WooCommerce.
                    </span>
                  </li>
                  <li className="flex items-start">
                    <span className="font-medium mr-2">•</span>
                    <span>
                      <strong>Khách hàng:</strong> Đồng bộ thông tin khách hàng từ các website WooCommerce.
                    </span>
                  </li>
                </ul>
              </div>

              <div>
                <h4 className="font-medium">Lưu ý quan trọng</h4>
                <ul className="mt-2 space-y-2 text-sm text-muted-foreground">
                  <li className="flex items-start">
                    <span className="font-medium mr-2">•</span>
                    <span>Đồng bộ tự động sẽ áp dụng cho tất cả các website đang hoạt động.</span>
                  </li>
                  <li className="flex items-start">
                    <span className="font-medium mr-2">•</span>
                    <span>Quá trình đồng bộ có thể mất thời gian tùy thuộc vào lượng dữ liệu.</span>
                  </li>
                  <li className="flex items-start">
                    <span className="font-medium mr-2">•</span>
                    <span>Lịch sử đồng bộ tự động sẽ được hiển thị trong tab "Lịch sử đồng bộ".</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
