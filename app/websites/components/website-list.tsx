"use client"

import { useState } from "react"
import type { Website } from "@/types/supabase"
import { deleteWebsite } from "../actions"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Badge } from "@/components/ui/badge"
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog"
import { toast } from "@/components/ui/use-toast"
import { Edit, Globe, MoreVertical, Trash2, RefreshCw, PlusCircle } from "lucide-react"
import Link from "next/link"

interface WebsiteListProps {
  websites: Website[]
}

export function WebsiteList({ websites }: WebsiteListProps) {
  const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false)
  const [websiteToDelete, setWebsiteToDelete] = useState<Website | null>(null)
  const [isDeleting, setIsDeleting] = useState(false)

  // Xử lý xóa website
  const handleDelete = async () => {
    if (!websiteToDelete) return

    setIsDeleting(true)

    try {
      const result = await deleteWebsite(websiteToDelete.id)

      if (result.success) {
        toast({
          title: "Xóa website thành công",
          description: `Website "${websiteToDelete.name}" đã được xóa.`,
        })
      } else {
        throw new Error(result.error || "Có lỗi xảy ra khi xóa website")
      }
    } catch (error) {
      toast({
        title: "Lỗi",
        description: error instanceof Error ? error.message : "Có lỗi xảy ra khi xóa website",
        variant: "destructive",
      })
    } finally {
      setIsDeleting(false)
      setIsDeleteDialogOpen(false)
      setWebsiteToDelete(null)
    }
  }

  // Hiển thị trạng thái website
  const getStatusBadge = (status: string) => {
    switch (status) {
      case "active":
        return <Badge className="bg-green-500">Hoạt động</Badge>
      case "inactive":
        return <Badge variant="secondary">Không hoạt động</Badge>
      case "pending":
        return <Badge variant="outline">Đang xử lý</Badge>
      default:
        return <Badge variant="outline">{status}</Badge>
    }
  }

  // Định dạng thời gian
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString("vi-VN", {
      year: "numeric",
      month: "long",
      day: "numeric",
    })
  }

  if (websites.length === 0) {
    return (
      <Card>
        <CardContent className="flex flex-col items-center justify-center p-6">
          <Globe className="h-12 w-12 text-muted-foreground mb-4" />
          <p className="text-muted-foreground text-center mb-4">Chưa có website nào được thêm vào hệ thống.</p>
          <Button asChild>
            <Link href="/websites/new">
              <PlusCircle className="mr-2 h-4 w-4" />
              Thêm Website
            </Link>
          </Button>
        </CardContent>
      </Card>
    )
  }

  return (
    <>
      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        {websites.map((website) => (
          <Card key={website.id}>
            <CardHeader className="pb-3">
              <div className="flex justify-between items-start">
                <CardTitle className="text-xl truncate" title={website.name}>
                  {website.name}
                </CardTitle>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon">
                      <MoreVertical className="h-4 w-4" />
                      <span className="sr-only">Tùy chọn</span>
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuLabel>Tùy chọn</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem asChild>
                      <Link href={`/websites/${website.id}`}>
                        <Edit className="mr-2 h-4 w-4" />
                        Chỉnh sửa
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                      <Link href={`/websites/${website.id}/sync`}>
                        <RefreshCw className="mr-2 h-4 w-4" />
                        Đồng bộ dữ liệu
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                      className="text-red-600"
                      onClick={() => {
                        setWebsiteToDelete(website)
                        setIsDeleteDialogOpen(true)
                      }}
                    >
                      <Trash2 className="mr-2 h-4 w-4" />
                      Xóa
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
              <CardDescription>
                <a
                  href={website.url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-blue-500 hover:underline flex items-center"
                >
                  <Globe className="h-3 w-3 mr-1" />
                  {new URL(website.url).hostname}
                </a>
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Trạng thái:</span>
                  {getStatusBadge(website.status)}
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Đồng bộ lần cuối:</span>
                  <span>{website.last_sync ? formatDate(website.last_sync) : "Chưa đồng bộ"}</span>
                </div>
              </div>
            </CardContent>
            <CardFooter className="pt-1">
              <Button asChild variant="outline" className="w-full">
                <Link href={`/websites/${website.id}`}>
                  <Edit className="mr-2 h-4 w-4" />
                  Chỉnh sửa
                </Link>
              </Button>
            </CardFooter>
          </Card>
        ))}
      </div>

      <AlertDialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Xác nhận xóa website</AlertDialogTitle>
            <AlertDialogDescription>
              Bạn có chắc chắn muốn xóa website "{websiteToDelete?.name}"? Hành động này không thể hoàn tác và sẽ xóa
              tất cả dữ liệu liên quan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel disabled={isDeleting}>Hủy</AlertDialogCancel>
            <AlertDialogAction onClick={handleDelete} disabled={isDeleting} className="bg-red-600 hover:bg-red-700">
              {isDeleting ? "Đang xóa..." : "Xóa website"}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </>
  )
}
