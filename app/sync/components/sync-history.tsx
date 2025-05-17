"use client"

import { useState } from "react"
import { cancelSync, deleteSyncLog } from "../actions"
import { Button } from "@/components/ui/button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "@/components/ui/alert-dialog"
import { useToast } from "@/components/ui/use-toast"
import { Badge } from "@/components/ui/badge"
import { Loader2, Trash2, XCircle } from "lucide-react"
import { formatDistanceToNow } from "date-fns"
import { vi } from "date-fns/locale"

interface SyncHistoryProps {
  syncLogs: any[]
  showWebsiteName?: boolean
}

export function SyncHistory({ syncLogs, showWebsiteName = false }: SyncHistoryProps) {
  const { toast } = useToast()
  const [isLoading, setIsLoading] = useState<Record<string, boolean>>({})
  const [isDeleting, setIsDeleting] = useState<Record<string, boolean>>({})

  // Xử lý hủy đồng bộ
  async function handleCancelSync(syncLogId: string) {
    setIsLoading((prev) => ({ ...prev, [syncLogId]: true }))

    try {
      const result = await cancelSync(syncLogId)

      if (result.success) {
        toast({
          title: "Đã hủy đồng bộ",
          description: "Quá trình đồng bộ đã được hủy thành công.",
        })
      } else {
        toast({
          title: "Lỗi",
          description: result.error || "Có lỗi xảy ra khi hủy đồng bộ.",
          variant: "destructive",
        })
      }
    } catch (error) {
      toast({
        title: "Lỗi",
        description: "Có lỗi xảy ra khi hủy đồng bộ.",
        variant: "destructive",
      })
    } finally {
      setIsLoading((prev) => ({ ...prev, [syncLogId]: false }))
    }
  }

  // Xử lý xóa bản ghi đồng bộ
  async function handleDeleteSyncLog(syncLogId: string) {
    setIsDeleting((prev) => ({ ...prev, [syncLogId]: true }))

    try {
      const result = await deleteSyncLog(syncLogId)

      if (result.success) {
        toast({
          title: "Đã xóa bản ghi",
          description: "Bản ghi đồng bộ đã được xóa thành công.",
        })
      } else {
        toast({
          title: "Lỗi",
          description: result.error || "Có lỗi xảy ra khi xóa bản ghi đồng bộ.",
          variant: "destructive",
        })
      }
    } catch (error) {
      toast({
        title: "Lỗi",
        description: "Có lỗi xảy ra khi xóa bản ghi đồng bộ.",
        variant: "destructive",
      })
    } finally {
      setIsDeleting((prev) => ({ ...prev, [syncLogId]: false }))
    }
  }

  // Hiển thị loại dữ liệu
  function getEntityTypeLabel(entityType: string) {
    switch (entityType) {
      case "products":
        return "Sản phẩm"
      case "orders":
        return "Đơn hàng"
      case "customers":
        return "Khách hàng"
      case "all":
        return "Tất cả"
      default:
        return entityType
    }
  }

  // Hiển thị trạng thái
  function getStatusBadge(status: string) {
    switch (status) {
      case "running":
        return (
          <Badge variant="outline" className="bg-blue-50 text-blue-700 hover:bg-blue-50">
            Đang chạy
          </Badge>
        )
      case "success":
        return (
          <Badge variant="outline" className="bg-green-50 text-green-700 hover:bg-green-50">
            Thành công
          </Badge>
        )
      case "failed":
        return (
          <Badge variant="outline" className="bg-red-50 text-red-700 hover:bg-red-50">
            Thất bại
          </Badge>
        )
      case "cancelled":
        return (
          <Badge variant="outline" className="bg-orange-50 text-orange-700 hover:bg-orange-50">
            Đã hủy
          </Badge>
        )
      default:
        return <Badge variant="outline">{status}</Badge>
    }
  }

  // Định dạng thời gian
  function formatTime(dateString: string) {
    if (!dateString) return "N/A"

    try {
      return formatDistanceToNow(new Date(dateString), {
        addSuffix: true,
        locale: vi,
      })
    } catch (error) {
      return dateString
    }
  }

  if (syncLogs.length === 0) {
    return (
      <div className="text-center py-6">
        <p className="text-muted-foreground">Chưa có lịch sử đồng bộ nào.</p>
      </div>
    )
  }

  return (
    <div className="overflow-x-auto">
      <Table>
        <TableHeader>
          <TableRow>
            {showWebsiteName && <TableHead>Website</TableHead>}
            <TableHead>Loại dữ liệu</TableHead>
            <TableHead>Trạng thái</TableHead>
            <TableHead>Số lượng</TableHead>
            <TableHead>Bắt đầu</TableHead>
            <TableHead>Kết thúc</TableHead>
            <TableHead className="text-right">Thao tác</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {syncLogs.map((log) => (
            <TableRow key={log.id}>
              {showWebsiteName && <TableCell className="font-medium">{log.website?.name || "N/A"}</TableCell>}
              <TableCell>{getEntityTypeLabel(log.entity_type)}</TableCell>
              <TableCell>{getStatusBadge(log.status)}</TableCell>
              <TableCell>{log.items_count || 0}</TableCell>
              <TableCell>{formatTime(log.started_at)}</TableCell>
              <TableCell>{log.completed_at ? formatTime(log.completed_at) : "N/A"}</TableCell>
              <TableCell className="text-right">
                <div className="flex justify-end space-x-2">
                  {log.status === "running" && (
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => handleCancelSync(log.id)}
                      disabled={isLoading[log.id]}
                    >
                      {isLoading[log.id] ? (
                        <Loader2 className="h-4 w-4 animate-spin" />
                      ) : (
                        <XCircle className="h-4 w-4" />
                      )}
                    </Button>
                  )}

                  <AlertDialog>
                    <AlertDialogTrigger asChild>
                      <Button variant="outline" size="sm" disabled={isDeleting[log.id]}>
                        {isDeleting[log.id] ? (
                          <Loader2 className="h-4 w-4 animate-spin" />
                        ) : (
                          <Trash2 className="h-4 w-4" />
                        )}
                      </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                      <AlertDialogHeader>
                        <AlertDialogTitle>Xóa bản ghi đồng bộ?</AlertDialogTitle>
                        <AlertDialogDescription>
                          Bạn có chắc chắn muốn xóa bản ghi đồng bộ này? Hành động này không thể hoàn tác.
                        </AlertDialogDescription>
                      </AlertDialogHeader>
                      <AlertDialogFooter>
                        <AlertDialogCancel>Hủy</AlertDialogCancel>
                        <AlertDialogAction onClick={() => handleDeleteSyncLog(log.id)}>Xóa</AlertDialogAction>
                      </AlertDialogFooter>
                    </AlertDialogContent>
                  </AlertDialog>
                </div>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}
